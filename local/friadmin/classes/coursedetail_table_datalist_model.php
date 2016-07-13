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
    /**
     * @param       $courseId
     * @throws      Exception
     * @updateDate  24/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add Comments
     * Add Exception
     * Clean Code
     */
    public function __construct($courseId) {
        /* Variables    */
        global $CFG;
        $filterData = null;

        try {
            // Create the data object and set the first values
            parent::__construct();

            /* Create Filter structure  */
            $filterData = new \stdClass();
            $filterData->selcourseid    = $courseId;

            /* Add Filter structure     */
            $this->filterdata           = $filterData;

            /* Add Fields   */
            $this->fields = array('courseid', 'name', 'summary', 'targetgroup', 'date',
                                  'time', 'length', 'municipality', 'sector', 'location', 'responsible',
                                  'teacher', 'priceinternal', 'priceexternal', 'priceinternal','priceexternal','seats', 'deadline');
        }catch (Exception $ex) {
            throw $ex;
        }//try_Catch
    }//constructor

    /**
     * @return          Bool
     * @throws          Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Add datalist filters
     */
    protected function add_datalist_filters() {
        /* Variables    */

        try {
            if (is_null($this->filterdata)) {
                return false;
            }

            if (!empty($this->filterdata->selcourseid)) {
                $this->datalist->where('courseid', '== ' . $this->filterdata->selcourseid);
            }

            return true;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//add_datalist_filters
}
