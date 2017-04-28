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

//namespace mod_registerattendance;

defined('MOODLE_INTERNAL') || die;

//use renderable;
//use renderer_base;
//use stdClass;

/**
 * Model class for the mod_registerattendance course_list table
 *
 * @package         mod
 * @subpackage      registerattendance
 * @copyright       2016 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_registerattendance_view_table_datalist_model extends local_efaktor_model {
    /**
     * Construct the view page datalist model
     *
     * @param object|null $filterdata
     * @param object      $sort
     * @param int         $start      The first row to show
     * @param int         $rowstoshwo The the number of rows to show
     */
    public function __construct($filterdata, $sort, $start = 0, $rowstoshwo = 0) {
        global $CFG;

        // Create the data object and set the first values
        parent::__construct();

        /* Set the data */
        $this->filterdata = $filterdata;
        $this->sort = $sort;

        /* Set up the fields    */
        $this->fields = array('id', 'firstname', 'lastname', 'municipality', 'workplace', 'attended');
        $this->set_fixture_data($CFG->dirroot .
            '/mod/registerattendance/fixtures/registerattendance_view.json', 'data', $this->fields);

        // If paging then reduce the data to the actual page.
        if ($start) {
            $this->prepare_paged_data($start, $rowstoshwo);
        }

    }//constructor


    /**
     * Reduce the data to the actual requested page
     *
     * @param int $start      The first row to show
     * @param int $rowstoshwo The the number of rows to show
     */
    public function prepare_paged_data($start, $rowstoshow) {
        $this->data = array_slice($this->data, $start, $rowstoshow);
    }

    /**
     * Add datalist filters
     *
     * @return bool
     */
    protected function add_datalist_filters() {
        if (is_null($this->filterdata)) {
            return false;
        }

        if (!empty($this->filterdata->selfirstname)) {
            $this->datalist->where('firstname', '.*' . $this->filterdata->selfirstname . '.*');
        }
        if (!empty($this->filterdata->sellastname)) {
            $this->datalist->where('lastname', '.*' . $this->filterdata->sellastname . '.*');
        }
        if (!empty($this->filterdata->selmunicipality)) {
            $this->datalist->where('municipality', '.*' . $this->filterdata->selmunicipality . '.*');
        }
        if (!empty($this->filterdata->selworkplace)) {
            $this->datalist->where('workplace', '.*' . $this->filterdata->selname . '.*');
        }

        return true;
    }
}
