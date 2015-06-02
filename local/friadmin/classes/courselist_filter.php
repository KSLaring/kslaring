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

    protected $userleveloneids = array();

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
        $leveloneobjs = $this->get_levelone_municipalities($userid);

        // Get the categories for which the user has admin rights
        $catadmin = $this->get_categories_admin();

        // Use only municipalities where the user has admin rights on the categories
        // and with equal category and municipality names
        $leveloneobjsfiltered = array();
        foreach ($leveloneobjs as $obj) {
            foreach ($catadmin as $catname) {
                if (strpos($catname, $obj->name) !== false) {
                    $result['municipality'][$obj->name] = $obj->name;
                    $this->userleveloneids[] = $obj->id;
                    $leveloneobjsfiltered[] = $obj;
                }
            }
        }

        if (!empty($leveloneobjsfiltered)) {
            // Get the sectors for the relevant municipalities via inustrycodes
            $leveltwoobjs = $this->get_leveltwo_sectors($leveloneobjsfiltered);
            foreach ($leveltwoobjs as $obj) {
                if (!in_array($obj->name, $result['sector'])) {
                    $result['sector'][$obj->name] = $obj->name;
                }
            }

            // Get the locations for the relevant municipalities via levelone ids
            $locationsobjs = $this->get_locations($leveloneobjsfiltered);
            foreach ($locationsobjs as $obj) {
                if (!in_array($obj->name, $result['location'])) {
                    $result['location'][$obj->name] = $obj->name;
                }
            }
        }

        return $result;
    }

    /**
     * Get course categories where the user is admin
     *
     * @param Int $userid The user id
     *
     * @return mixed Array|null Null or array with the category names
     */
    protected function get_categories_admin() {
        global $CFG, $DB;
        $result = null;

        // Get the course categories where the user is admin
        require_once $CFG->dirroot . '/lib/coursecatlib.php';
        $courecats = $DB->get_records('course_categories');
        $coursecat_names = array();
        foreach ($courecats as $courecat) {
            $coursecat_obj = coursecat::get($courecat->id);
            if ($coursecat_obj->has_manage_capability()) {
                $coursecat_names[] = $courecat->name;
            }
        }

        if (!empty($coursecat_names)) {
            $result = $coursecat_names;
        }

        return $result;
    }

    /**
     * Get the levelone locations with id, name and industrycode
     *
     * @param Int $userid The user id
     *
     * @return mixed Array|null The levelone locations
     */
    protected function get_levelone_municipalities($userid) {
        global $CFG, $DB;
        $ids = array();
        $leveloneobjs = null;

        // Get the user competences
        require_once $CFG->dirroot . '/user/profile/field/competence/competencelib.php';
        $competences = Competence::Get_CompetenceData($userid);

        // Get the levleone ids
        foreach ($competences as $comp) {
            if (!in_array($comp->levelOne, $ids)) {
                $ids[] = $comp->levelOne;
            }
        }

        // Get the levelone ids, names and industrycodes with given ids
        if (!empty($ids)) {
            $sql = "
                SELECT
                  id,
                  name,
                  industrycode
                FROM {report_gen_companydata}
                WHERE id
            ";
            list($in, $params) = $DB->get_in_or_equal($ids);

            $leveloneobjs = $DB->get_records_sql($sql . $in, $params);
        }

        return $leveloneobjs;
    }

    /**
     * Get the sectors with id, name and industrycode
     *
     * @param Object $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     */
    protected function get_leveltwo_sectors($leveloneobjsfiltered) {
        global $DB;
        $industrycodes = array();
        $leveltwoobjs = null;

        // Get the industriecodes
        foreach ($leveloneobjsfiltered as $obj) {
            if (!in_array($obj->industrycode, $industrycodes)) {
                $industrycodes[] = $obj->industrycode;
            }
        }

        // Get the leveltwo ids, names and industrycodes with given industrycodes
        if (!empty($industrycodes)) {
            $sql = "
                SELECT
                  id,
                  name,
                  industrycode
                FROM {report_gen_companydata}
                WHERE hierarchylevel = 2
                  AND industrycode
            ";
            list($in, $params) = $DB->get_in_or_equal($industrycodes);

            $leveltwoobjs = $DB->get_records_sql($sql . $in, $params);
        }

        return $leveltwoobjs;
    }

    /**
     * Get the locations with id, name
     *
     * @param Object $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     */
    protected function get_locations($leveloneobjsfiltered) {
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
}
