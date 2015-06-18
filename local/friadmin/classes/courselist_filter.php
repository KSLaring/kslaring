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
 * Class containing data for the local_friadmin course_list selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_courselist_filter extends local_friadmin_widget implements renderable {

    /* Level One    */
    protected $userleveloneids  = array();
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

    // The Moodle form
    protected $mform = null;

    // The returned form data
    protected $fromform = null;

    /**
     * Construct the courselist_page renderable.
     */
    public function __construct() {
        global $SESSION;

        if (!isset($SESSION->friadmin_courselist_filtering)) {
            $SESSION->friadmin_courselist_filtering = array();
        }

        // Create the data object and set the first values
        parent::__construct();

//        $customdata = $this->get_fixture('friadmin_coursefilter');
        $customdata = $this->get_user_locationdata();

        $mform = new local_friadmin_courselist_filter_form(null, $customdata, 'post', '',
            array('id' => 'mform-coursefilter'));

        $this->mform = $mform;

        if ($fromform = $mform->get_data()) {
            $this->fromform = $fromform;
            $SESSION->friadmin_courselist_filtering = $fromform;
//            local_logger\console::log($fromform, "Filter - Fromform");
        } else if (!empty($SESSION->friadmin_courselist_filtering)) {
            $this->fromform = $SESSION->friadmin_courselist_filtering;
            $mform->set_defaults($SESSION->friadmin_courselist_filtering);
//            local_logger\console::log($SESSION->friadmin_courselist_filtering,
//                "Filter - SESSION friadmin_courselist_filtering");
        }
    }

    /**
     * Get the returned form data, if any
     */
    public function get_userleveloneids() {
        return $this->userleveloneids;
    }

    /**
     * @return          array
     *
     * @creationDate    17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Return all categories connected with user
     */
    public function get_myCategories() {
        return $this->myCategories;
    }//get_myCategories

    /**
     * Get the returned form data, if any
     */
    public function get_fromform() {
        return $this->fromform;
    }

    /**
     * Render the form and set the data
     */
    public function render() {
        $this->data->content = $this->mform->render();
    }

    /**
     * Set the default form values
     *
     * The associative $defaults: array 'elementname' => 'defaultvalue'
     *
     * @param Array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $this->mform->set_defaults($defaults);
    }

    /**
     * Get the user related location data
     *
     * @param Int $userid The user id
     *
     * @return Array $result The user location data
     */
    public function get_user_locationdata($userid = null) {
        global $CFG, $DB;

        $result = array(
            'municipality' => array(),
            'sector' => array(),
            'location' => array(),
            'from' => null,
            'to' => null
        );

        if (is_null($userid)) {
            global $USER;

            $userid = $USER->id;
        }

        // Get the competence related municipalities
        // The $leveloneobjs array contains objects with
        // id, name and industrycode properties.
        $leveloneobjs = local_friadmin_helper::get_levelone_municipalities($userid);

        /**
         * @updateDate  17/06/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Get all my municipalities
         */
        $leveloneobjsfiltered = array();
        foreach ($leveloneobjs as $obj) {
            $result['municipality'][$obj->id] = $obj->name;
            $this->userleveloneids[] = $obj->id;
            $leveloneobjsfiltered[] = $obj;
        }

        /**
         * @updateDate  17/06/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Get all categories where the user is a super user
         */
        $this->myCategories = local_friadmin_helper::getMyCategories();

        // Get the categories for which the user has admin rights
        //$catadmin = local_friadmin_helper::get_categories_admin();

        // Use only municipalities where the user has admin rights on the course categories
        // and where the municipality name is contained in the category name
        //$leveloneobjsfiltered = array();
        //foreach ($leveloneobjs as $obj) {
        //    foreach ($catadmin as $catname) {
        //        //if (strpos($catname, $obj->name) !== false) {
        //            $result['municipality'][$obj->name] = $obj->name;
        //            $this->userleveloneids[] = $obj->id;
        //            $leveloneobjsfiltered[] = $obj;
        //}
        //    }
        //}

        if (!empty($leveloneobjsfiltered)) {
            // Get the sectors for the relevant municipalities via inustrycodes
            $leveltwoobjs = local_friadmin_helper::get_leveltwo_sectors($leveloneobjsfiltered);

            foreach ($leveltwoobjs as $obj) {
                if (!in_array($obj->name, $result['sector'])) {
                    $result['sector'][$obj->id] = $obj->name;
                }
            }

            // Get the locations for the relevant municipalities via levelone ids
            $locationsobjs = $this->get_locations($leveloneobjsfiltered);
            foreach ($locationsobjs as $obj) {
                if (!in_array($obj->name, $result['location'])) {
                    $result['location'][$obj->id] = $obj->name;
                }
            }
        }

        return $result;
    }

    /**
     * Get the locations with id, name
     *
     * @param Object $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     */
    protected function get_locations_old($leveloneobjsfiltered) {
        global $DB;
        $ids = array();
        $locations = null;

        // Get the municipality ids
        foreach ($leveloneobjsfiltered as $obj) {
            if (!in_array($obj->id, $ids)) {
                $ids[] = $obj->id;
            }
        }

        // Get the location ids and names with given municipality ids
        if (!empty($ids)) {
            $sql = "
                SELECT
                  id,
                  name
                FROM {course_locations}
                WHERE levelone
            ";
            list($in, $params) = $DB->get_in_or_equal($ids);

            $locations = $DB->get_records_sql($sql . $in, $params);
        }

        return $locations;
    }

    /**
     * Get the locations with id, name
     *
     * @param Object $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     */
    /**
     * @param        $leveloneobjsfiltered
     * @return       array|null
     * @throws       Exception
     *
     * @updateDate  18/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
     * Add comments, exception...
     */
    protected function get_locations($leveloneobjsfiltered) {
       /* Variables */
        global $DB;
        $ids        = array();
        $locations  = null;

        try {
            if ($leveloneobjsfiltered) {
                /* Get the municipality ids */
                foreach ($leveloneobjsfiltered as $obj) {
                    if (!in_array($obj->id, $ids)) {
                        $ids[] = $obj->id;
                    }
                }

                /* Get the location ids and names with given municipality ids */
                if (!empty($ids)) {
                    /* SQL Instruction  */
                    $sql = "  SELECT    id,
                                        name
                              FROM      {course_locations}
                              WHERE     levelone ";

                    /* Get search criteria  */
                    list($in, $params) = $DB->get_in_or_equal($ids);

                    /* Execute  */
                    $locations = $DB->get_records_sql($sql . $in, $params);
                }//if_Empty
            }//if_leveloneobjsfiltered

            return $locations;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_locations
}
