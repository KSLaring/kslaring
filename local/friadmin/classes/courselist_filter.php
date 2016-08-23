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
    protected $userleveloneids = array();
    /**
     * @var         array
     *
     * @updateDate  17/06/2015
     * @author      eFaktor     (fbv)
     *
     * Description
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
    /**
     * @updateDate  23/06/205
     * @author      eFaktor     (fbv)
     *
     * Description
     * Clean code
     * Rebuild logical with SESSION variable for the filter
     */
    public function __construct() {
        /* Variables    */
        global $SESSION;

        // Create the data object and set the first values
        parent::__construct();

        // Get the filtered user locationdata.
        $customdata = $this->get_user_locationdata();

        // Set tomorrow as the default from date for the search.
        if (!isset($customdata['seltimefrom'])) {
            $customdata['seltimefrom'] = (time() + 3600 * 24);
        }
        $this->fromform = $customdata;

        /**
         * @updateDate  23/06/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Clean code.
         * Get values filter but default and set up
         */
        if (!isset($SESSION->filterData)) {
            $SESSION->filterData = array();
        }//if_filterData_SESSION

        $mform = new local_friadmin_courselist_filter_form(null, $customdata, 'post', '',
            array('id' => 'mform-coursefilter'));

        /**
         * @updateDate  23/06/2015
         * @author      eFaktor     (fbv)
         *
         * Description
         * Add check classroom checkbox
         */
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
     * @param array $defaults The default values
     */
    public function set_defaults($defaults = array()) {
        $this->mform->set_defaults($defaults);
    }

    /**
     * @param       null $userId
     *
     * @return           array
     * @throws           Exception
     *
     * @creationDate
     * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
     *
     * Description
     * Get the user related location data
     *
     * @updateDate      22/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add a new parameter in the filter -- classroom format
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
                'municipality' => array(),
                'sector' => array(),
                'location' => array(),
                'from' => null,
                'to' => null,
                'classroom' => true,
                'elearning' => true,
            );

            if (is_null($userId)) {
                $userId = $USER->id;
            }//id_userid

            // Get the competence related municipalities
            // The $leveloneobjs array contains objects with
            // id, name and industrycode properties.
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
     * @param Object $leveloneobjsfiltered The array of objects with the municipalities
     *
     * @return mixed Array|null The array with the sector objects
     */
    /**
     * @param        $leveloneobjsfiltered
     *
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

    /**
     * Get the locations for the given sectors with id, name
     *
     * @param int $sectorid The selected sector id
     *
     * @return mixed Array|null The array with the sector objects
     *
     * @throws  Exception
     */
    protected function get_locations_for_sector($sectorid) {
        global $DB;
        $locations = null;

        try {
            if ($sectorid) {
                // Get the location ids and names for the given sector id.
                $sql = "SELECT	lo.id,
                                lo.name
                        FROM {course_locations} lo
                        -- LEVLE TWO
                        JOIN {report_gen_company_relation}	cr_two	
                          ON cr_two.parentid = lo.levelone
                          AND cr_two.companyid = :sector_selected
                        JOIN {report_gen_companydata}	co_two 
                          ON co_two.id = cr_two.companyid
                        -- LEVEL ONE
                        JOIN {report_gen_company_relation}	cr_one 
                          ON cr_one.companyid = cr_two.parentid
                        -- LEVEL ZERO
                        JOIN {report_gen_company_relation}	cr_zero 
                          ON cr_zero.companyid = cr_one.companyid  ";

                // Get search criteria.
                $params = array('sector_selected' => $sectorid);

                $locations = $DB->get_records_sql($sql, $params);
            }

            return $locations;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_locations_for_sector
}
