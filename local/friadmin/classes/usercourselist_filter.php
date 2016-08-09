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
 * Class containing data for the local_friadmin usercourse_list selection area
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_usercourselist_filter extends local_friadmin_widget implements renderable {

    /* Level One    */
    protected $userleveloneids = array();

    /**
     * Categories connected with user
     */
    protected $myCategories = array();

    // The Moodle form
    protected $mform = null;

    // The returned form data
    protected $fromform = null;

    /**
     * Construct the courselist_page renderable.
     */
    public function __construct() {
        /* Variables    */
        global $SESSION;

        // Create the data object and set the first values
        parent::__construct();

        $customdata = $this->get_user_locationdata();
        $this->fromform = $customdata;
        if (!isset($SESSION->filterData)) {
            $SESSION->filterData = array();
        }//if_filterData_SESSION

        // If we are on the frontpage the URL contains '?redirect=0'.
        // Moodle strips the query from the URL for the mform action, so we need
        // to set the action for the frontpage when the form is set up.
        // On all other pages the action can be null -> Moodle handles it.
        $action = qualified_me();
        if (strpos($action, '?redirect=0') === false) {
            $action = null;
        }

        $mform = new local_friadmin_usercourselist_filter_form($action, $customdata, 'post',
            '', array('id' => 'mform-coursefilter'));

        $this->mform = $mform;
        if ($fromform = $mform->get_data()) {
            $SESSION->filterData = (Array)$fromform;
            $this->fromform = $SESSION->filterData;
        } else if ($SESSION->filterData) {
            if (!isset($SESSION->filterData['classroom'])) {
                $SESSION->filterData['classroom'] = false;
            }
            /**
             * @updateDate  02/2/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add check elearning
             */
            if (!isset($SESSION->filterData['elearning'])) {
                $SESSION->filterData['elearning'] = false;
            }
            $this->fromform = $SESSION->filterData;
            $mform->set_defaults($SESSION->filterData);
        }//if_form_data
    }

    /**
     * Get the user levelone ids
     */
    public function get_userleveloneids() {
        return $this->userleveloneids;
    }

    /**
     * Return all categories connected with user
     *
     * @return          array
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
     * @param array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $this->mform->set_defaults($defaults);
    }

    /**
     * Get the user related location data
     *
     * @param       null $userId
     *
     * @return           array
     * @throws           Exception
     */
    public function get_user_locationdata($userId = null) {
        /* Variables    */
        global $USER, $SESSION;
        $result = null;
        $leveloneobjs = null;
        $leveloneobjsfiltered = array();

        try {
            /* Result Structure */
            /**
             * @updateDate  02/12/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Add option only eLearning courses
             */
            $result = array(
                'municipality'  => array(),
                'sector'        => array(),
                'location'      => array(),
                'from'          => null,
                'to'            => null,
                'classroom'     => true,
                'elearning'     => true,
            );

            if (!isloggedin()) {
                return $result;
            }

            if (is_null($userId)) {
                $userId = $USER->id;
            }//id_userid

            // Get the competence related municipalities
            // The $leveloneobjs array contains objects with
            // id, name and industry code properties.
            $leveloneobjs = local_friadmin_helper::get_levelone_municipalities($userId);

            // Check if a new municipality has been selected by the user which is not
            // yet saved in the session. The session data is saved after the form is
            // created.
            $sessionselmunicipality = 0;
            if (!empty($SESSION->filterData['selmunicipality'])) {
                $sessionselmunicipality = $SESSION->filterData['selmunicipality'];
            }
            $selmunicipality = optional_param('selmunicipality', $sessionselmunicipality, PARAM_INT);

            /**
             * @updateDate  17/06/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Get all my municipalities
             */
            foreach ($leveloneobjs as $obj) {
                $result['municipality'][$obj->id] = $obj->name;
                $this->userleveloneids[] = $obj->id;

                // If a municipality has been selected then use only that one.
                if ($selmunicipality) {
                    if ($selmunicipality == $obj->id) {
                        $leveloneobjsfiltered[] = $obj;
                    }
                } else {
                    $leveloneobjsfiltered[] = $obj;
                }
            }//for_levelone_obj

            /**
             * @updateDate  17/06/2015
             * @author      eFaktor     (fbv)
             *
             * Description
             * Get all categories where the user is a super user
             */
            $this->myCategories = local_friadmin_helper::getMyCategories();

            if (!empty($leveloneobjsfiltered)) {
                // Get the sectors for the relevant municipalities via industrycodes.
                $leveltwoobjs = local_friadmin_helper::get_leveltwo_sectors($leveloneobjsfiltered);

                foreach ($leveltwoobjs as $obj) {
                    if (!in_array($obj->id, $result['sector'])) {
                        $result['sector'][$obj->id] = $obj->name;
                    }
                }

                // Check if a new sector has been selected by the user which is not
                // yet saved in the session. The session data is saved after the form is
                // created.
                $sessionsectorid = 0;
                $locationsobjs = array();
                if (!empty($SESSION->filterData['selsector'])) {
                    $sessionsectorid = $SESSION->filterData['selsector'];
                }
                $sectorid = optional_param('selsector', $sessionsectorid, PARAM_INT);

                if (!$sectorid) {
                    // Get the locations for the relevant municipalities via levelone ids
                    $locationsobjs = $this->get_locations($leveloneobjsfiltered);
                } else {
                    $locationsobjs = $this->get_locations_for_sector($sectorid);
                }

                foreach ($locationsobjs as $obj) {
                    if (!in_array($obj->id, $result['location'])) {
                        $result['location'][$obj->id] = $obj->name;
                    }
                }
            }//obj_filteref

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_user_locationdata

    /**
     * Get the locations with id, name
     *
     * @param Array $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     * @throws Exception
     */
    protected function get_locations($leveloneobjsfiltered) {
        /* Variables */
        global $DB;
        $ids = array();
        $locations = null;

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
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_locations
}
