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
class local_friadmin_coursedetail_table_datalist_model extends local_efaktor_model {

    /**
     * Construct the coursedetail_page renderable.
     */
    public function __construct($courseid) {
        global $CFG;

        // Create the data object and set the first values
        parent::__construct();

        $filterdata = new \stdClass();
        $filterdata->selcourseid = $courseid;
        $this->filterdata = $filterdata;

        $this->fields = array('courseid', 'name', 'summary', 'targetgroup', 'date',
            'time', 'length', 'municipality', 'sector', 'location', 'responsible',
            'teacher', 'priceinternal', 'priceexternal', 'seats', 'deadline');
        $this->set_fixture_data(
            $CFG->dirroot . '/local/friadmin/fixtures/friadmin_courselist.json',
            'data', $this->fields);
    }

    /**
     * Add datalist filters
     *
     * @param Object $fromform The form result
     *
     * @return Bool
     */
    protected function add_datalist_filters() {

        if (is_null($this->filterdata)) {
            return false;
        }

        if (!empty($this->filterdata->selcourseid)) {
            $this->datalist->where('courseid', '== ' . $this->filterdata->selcourseid);
        }

        return true;
    }
}
