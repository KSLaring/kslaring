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

    // The related filter data returned from the form
    protected $filterdata = null;

    // The related sort data returned from the table
    protected $sort = null;

    // The query SQL
    protected $sql = "
      SELECT *
      FROM {friadmin_courselist_dev}
    ";

    /**
     * Construct the courselist_page renderable.
     */
    public function __construct($filterdata, $sort) {
        // Create the data object and set the first values
        parent::__construct();

        $this->filterdata = $filterdata;
        $this->sort = $sort;

        $this->get_data_from_db();
    }

    /**
     * Get the data from the DB and save it in the $data property
     */
    protected function get_data_from_db() {
        global $DB;

        list($sql, $params) = $this->add_filters($this->sql, $this->filterdata);

        if ($this->sort) {
            $sql .= ' ORDER BY ' . $this->sort;
        }

        $this->data = $DB->get_records_sql($sql, $params);
    }

    /**
     * Add filters.
     *
     * @param String $sql The SQL query
     *
     * @return Array An array with the extended SQL and the parameters
     */
    protected function add_filters($sql, $fromform = null) {
        global $DB;

        $params = array();

        if (is_null($fromform)) {
            return array($sql, $params);
        }

        $sql .= " WHERE id != 0";

        if (!empty($fromform->selmunicipality)) {
            $sql .= ' AND municipality = :selmunicipality';
            $params['selmunicipality'] = $fromform->selmunicipality;
        }
        if (!empty($fromform->selsector)) {
            $sql .= ' AND sector = :selsector';
            $params['selsector'] = $fromform->selsector;
        }
        if (!empty($fromform->sellocation)) {
            $sql .= ' AND location = :sellocation';
            $params['sellocation'] = $fromform->sellocation;
        }
        if (!empty($fromform->selname)) {
            $sql .= ' AND ' . $DB->sql_like('name', ':selname', false, false);
            $params['selname'] = "%" . $fromform->selname . "%";
        }
        if (!empty($fromform->seltimefrom)) {
            $sql .= ' AND date >= :seltimefrom';
            $params['seltimefrom'] = $fromform->seltimefrom;
        }
        if (!empty($fromform->seltimeto)) {
            $sql .= ' AND date <= :seltimeto';
            $params['seltimeto'] = $fromform->seltimeto;
        }

        return array($sql, $params);
    }
}
