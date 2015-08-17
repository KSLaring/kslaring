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
class local_friadmin_usercourselist_table_datalist_model extends local_efaktor_model {

    // The user municipality list
    protected $userleveloneids = null;

    /**
     * Construct the usercourselist_page renderable.
     *
     * @param           $userleveloneids
     * @param           $filterdata
     * @param           $sort
     *
     * @throws          Exception
     */
    public function __construct($userleveloneids, $filterdata, $sort) {
        global $CFG;

        try {
            // Create the data object and set the first values
            parent::__construct();

            /* Set the data */
            $this->userleveloneids = $userleveloneids;
            $this->filterdata = $filterdata;
            $this->sort = $sort;

            /* Set up the fields    */
            $this->fields = array('courseid', 'name', 'date', 'seats', 'deadline',
                'length', 'municipality', 'sector', 'location');
            $this->set_fixture_data(
                $CFG->dirroot . '/local/friadmin/fixtures/friadmin_courselist.json',
                'data', $this->fields);
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//constructor

    /**
     * Datalist filters
     */
    protected function add_datalist_filters() {
        /* Variables    */
        try {
            if (is_null($this->filterdata)) {
                return false;
            }

            if (!empty($this->filterdata->selmunicipality)) {
                $this->datalist->where('municipality', $this->filterdata->selmunicipality);
            }
            if (!empty($this->filterdata->selsector)) {
                $this->datalist->where('sector', $this->filterdata->selsector);
            }
            if (!empty($this->filterdata->sellocation)) {
                $this->datalist->where('location', $this->filterdata->sellocation);
            }
            if (!empty($this->filterdata->selname)) {
                $this->datalist->where('name', '.*' . $this->filterdata->selname . '.*');
            }
            if (!empty($this->filterdata->seltimefrom)) {
                $this->datalist->where('date', '>= ' . $this->filterdata->seltimefrom);
            }
            if (!empty($this->filterdata->seltimeto)) {
                $this->datalist->where('date', '<= ' . $this->filterdata->seltimeto);
            }

            return true;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }
}
