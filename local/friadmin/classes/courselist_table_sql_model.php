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
class local_friadmin_courselist_table_sql_model extends local_friadmin_widget {

    // The user municipality list
    protected $userleveloneids = null;

    /**
     * @var         array
     *
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Categories connected with user
     */
    protected  $myCategories    = array();

    // The related filter data returned from the form
    protected $filterdata = null;

    // The related sort data returned from the table
    protected $sort = null;

    /**
     * @var         string
     *
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Rewrite sql to get all the courses connected with user
     *
     * @updateDate  22/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add LEFT
     */
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
                                            IFNULL(MAX(e.customint1), 0) AS deadline
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
                           LEFT JOIN {report_gen_companydata} 	rgcmu ON 	rgcmu.id 		= cl.levelone ";


    /**
     * Construct the courselist_page renderable.
     */
    /**
     * @param           $userleveloneids
     * @param   null    $usercategories
     * @param           $filterdata
     * @param           $sort
     *
     * @updateDate      17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add the users categories parameter
     */
    public function __construct($userleveloneids, $usercategories = null,$filterdata, $sort) {
        // Create the data object and set the first values
        parent::__construct();

        $this->userleveloneids  = $userleveloneids;
        $this->filterdata       = $filterdata;
        $this->sort             = $sort;
        $this->myCategories     = $usercategories;

        $this->get_data_from_db();
    }

    /**
     * @throws      Exception
     *
     * @updateDate  22/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Rebuild the logical to get the data from DB and to add the filter criteria
     */
    protected function get_data_from_db() {
        /* Variables    */
        global $DB;
        $params     = array();
        $result     = null;
        $sqlWhere   = null;

        try {
            /* Add Filter   */
            list($sqlWhere,$params) = self::AddCriteria_Filter();
            if ($sqlWhere) {
                $this->sql .= $sqlWhere;
            }//if_sqlWhere

            /* Add Sort */
            if ($this->sort) {
                $this->sql .= ' ORDER BY ' . $this->sort;
            }//if_sort

            /* Execute  */
            $result = $DB->get_records_sql($this->sql,$params);

            $this->data = $result;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_data_from_db

    /**
     * @return          array
     * @throws          Exception
     *
     * @creationDate    22/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get the filter criteria
     */
    private function AddCriteria_Filter() {
        /* Variables    */
        global $DB;
        $params         = array();
        $categories     = null;
        $sqlWhere       = null;
        $filterData     = null;

        try {
            /* Categories   */
            if ($this->myCategories) {
                $categories = implode(',',array_keys($this->myCategories));
                if (!$sqlWhere) {
                    $sqlWhere = " WHERE ";
                }else {
                    $sqlWhere .= " AND ";
                }//if_selWhere
                $sqlWhere .= " c.category IN ($categories) ";
            }//if_Categories

            /* Get filter criteria from the form*/
            $filterData = $this->filterdata;
            if ($filterData) {
                /* Add Only Classroom Courses   */
                if (isset($filterData->classroom) && ($filterData->classroom)) {
                    if (!$sqlWhere) {
                        $sqlWhere = " WHERE ";
                    }else {
                        $sqlWhere .= " AND ";
                    }//if_selWhere
                    $sqlWhere .= " C.format like '%classroom%' ";

                    /* Municipality Filter  */
                    if (isset($filterData->selmunicipality) && ($filterData->selmunicipality)) {
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        }else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere
                        $sqlWhere .= " rgcmu.id = :selmunicipality ";
                        $params['selmunicipality'] = $filterData->selmunicipality;
                    }//if_selmunicipality

                    /* Location Filter      */
                    if (isset($filterData->sellocation) && ($filterData->sellocation)) {
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        }else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere
                        $sqlWhere .= " cl.id = :sellocation ";
                        $params['sellocation'] = $filterData->sellocation;
                    }//if_location

                    /* Sector Filter        */
                    if (isset($filterData->selsector) && ($filterData->selsector)) {
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        }else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere
                        $sqlWhere .= " rgcse.id = :selsector ";
                        $params['selsector'] = $filterData->selsector;
                    }//if_sector

                    /* From Time Filter     */
                    if (isset($filterData->seltimefrom) && ($filterData->seltimefrom)) {
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        }else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere
                        $sqlWhere .= " c.startdate >= :seltimefrom ";
                        $params['seltimefrom'] = $filterData->seltimefrom;
                    }//if_seltimeFrom

                    /* To Time Filter       */
                    if (isset($filterData->seltimeto) && ($filterData->seltimeto)) {
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        }else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere
                        $sqlWhere .= " c.startdate <= :seltimeto ";
                        $params['seltimeto'] = $filterData->seltimeto;
                    }//if_seltimeto

                    /* Course Name Filter   */
                    if (isset($filterData->selname) && ($filterData->selname)) {
                        if (!$sqlWhere) {
                            $sqlWhere = " WHERE ";
                        }else {
                            $sqlWhere .= " AND ";
                        }//if_selWhere
                        $sqlWhere .= $DB->sql_like('c.fullname', ':selname', false, false);
                        $params['selname'] = "%" . $filterData->selname . "%";
                    }//if_selName
                }//if_classroom
            }//if_filterData


            return array($sqlWhere,$params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//AddCategories_Filter

    /**
     * Get the data from the DB and save it in the $data property
     */
    /**
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add the user categories parameter
     */
    protected function get_data_from_db_old() {
        /* Variables    */
        global $DB;
        $result     = array();
        $categories = null;

        try {
            if (!empty($this->userleveloneids)) {
                list($sql, $params) = $this->add_filters($this->sql,$this->userleveloneids,$this->filterdata);

                /* Add Categories   */
                if ($this->myCategories) {
                    $categories = implode(',',array_keys($this->myCategories));
                    $sql .= " AND c.category IN ($categories) ";
                }//if_Categories

                /* Add Sort */
                if ($this->sort) {
                    $sql .= ' ORDER BY ' . $this->sort;
                }//if_sort
                $result = $DB->get_records_sql($sql,$params);
            }//if_empty

            $this->data = $result;
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

    protected function add_filters_old($sql, $userleveloneids,$fromform = null) {
        /* Variables    */
        global $DB;
        $params     = array();

        try {
            $sql .= " WHERE rgcmu.id ";

            list ($in, $params) = $DB->get_in_or_equal($userleveloneids, SQL_PARAMS_NAMED, 'userleveloneids');

            $sql .= $in;

            if (is_null($fromform)) {
                return array($sql, $params);
            }

            if (!empty($fromform->selmunicipality)) {
                $sql .= ' AND rgcmu.id = :selmunicipality';
                $params['selmunicipality'] = $fromform->selmunicipality;
            }
            if (!empty($fromform->selsector)) {
                $sql .= ' AND rgcse.id = :selsector';
                $params['selsector'] = $fromform->selsector;
            }
            if (!empty($fromform->sellocation)) {
                $sql .= ' AND cl.id = :sellocation';
                $params['sellocation'] = $fromform->sellocation;
            }
            if (!empty($fromform->selname)) {
                $sql .= ' AND ' . $DB->sql_like('c.fullname', ':selname', false, false);
                $params['selname'] = "%" . $fromform->selname . "%";
            }
            if (!empty($fromform->seltimefrom)) {
                $sql .= ' AND c.startdate >= :seltimefrom';
                $params['seltimefrom'] = $fromform->seltimefrom;
            }
            if (!empty($fromform->seltimeto)) {
                $sql .= ' AND c.startdate <= :seltimeto';
                $params['seltimeto'] = $fromform->seltimeto;
            }

            return array($sql, $params);
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }
}
