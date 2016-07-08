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
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->dirroot . '/user/profile/field/competence/competencelib.php');

//use moodle_url;
//use context_system;
//use stdClass;

/**
 * The Friadmin class with utility methods
 *
 * @package         local
 * @subpackage      friadmin
 * @copyright       2015 eFaktor
 * @author          Urs Hunkler {@link urs.hunkler@unodo.de}
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_friadmin_helper {

    /**
     * @return          bool
     * @throws          Exception
     *
     * @creationDate    07/01/2016
     * @author          eFaktor     (fbv)
     *
     * Description
     * Check if the user is a super user
     */
    public static function CheckCapabilityFriAdmin() {
        /* Variables    */
        global $DB, $USER;
        $contextCat     = null;
        $contextCourse  = null;
        $contextSystem  = null;
        $params         = null;
        $rdo            = null;
        $sql            = null;

        try {
            if (is_siteadmin($USER)) {
                return true;
            }
            /* Search Criteria  */
            $params = array();
            $params['user']         = $USER->id;
            $contextCat             = CONTEXT_COURSECAT;
            $contextCourse          = CONTEXT_COURSE;
            $contextSystem          = CONTEXT_SYSTEM;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 		r.id			= ra.roleid
                                                            AND		r.archetype		IN ('manager','coursecreator')
                        JOIN	{context}		    ct		ON		ct.id			= ra.contextid
                                                            AND		ct.contextlevel	IN ($contextCat,$contextCourse,$contextSystem)
                     WHERE		ra.userid 		= :user ";

            /* Execute  */
            $rdo = $DB->get_records_sql($sql,$params);
            if ($rdo) {
                return true;
            }else {
                return false;
            }//if_Rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//CheckCapabilityFriAdmin


    /**
     * @return          null
     * @throws          Exception
     *
     * @creationDate    17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all categories where the users ir a super user
     *
     * @updateDate      22/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Admin site can see everything
     */
    public static function getMyCategories() {
        /* Variables    */
        global $DB,$USER;
        $myCategories   = null;
        $categoriesLst  = null;
        $context        = null;
        $contextCat     = null;
        $contextCourse  = null;
        $contextSystem  = null;

        try {
            /* Get all the categories   */
            $categoriesLst = $DB->get_records('course_categories');

            /* Search Criteria  */
            $params = array();
            $params['user']     = $USER->id;
            $contextCat         = CONTEXT_COURSECAT;
            $contextCourse      = CONTEXT_COURSE;
            $contextSystem      = CONTEXT_SYSTEM;

            /* SQL Instruction  */
            $sql = " SELECT		ra.id
                     FROM		{role_assignments}	ra
                        JOIN	{role}				r		ON 	r.id			= ra.roleid
                                                            AND	r.archetype		IN ('manager','coursecreator')
                                                            AND r.shortname     = r.archetype
                        JOIN    {context}           ct      ON  ct.id			= ra.contextid
                                                            AND	ct.contextlevel	IN ($contextCat,$contextCourse,$contextSystem)
                     WHERE		ra.userid 		= :user
                        AND		ra.contextid 	= :context ";

            /* For each Category checks if the user has permissions */
            foreach ($categoriesLst as $category) {
                /* Get Context Category */
                $context = context_coursecat::instance($category->id);

                /**
                 * @updateDate      22/06/2015
                 * @author          eFaktor     (fbv)
                 *
                 * Description
                 * Admin site can see everything
                 */
                if (!has_capability('moodle/category:manage', $context)
                    &&
                    !has_capability('moodle/course:create', $context)) {
                    /* Execute   */
                    $params['context'] = $context->id;
                    $rdo = $DB->get_record_sql($sql,$params);
                    if ($rdo) {
                        /* Add Category */
                        $myCategories[$category->id] = $category->name;
                        /* If there are subcategories connected with, the user will also have permissions for them  */
                        self::getSubcategories($category->id,$myCategories);
                    }//if_Rdo
                }else {
                    /* Add Category */
                    $myCategories[$category->id] = $category->name;
                    /* If there are subcategories connected with, the user will also have permissions for them  */
                    self::getSubcategories($category->id,$myCategories);
                }
            }//for_Each_Category

            return $myCategories;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getMyCategories

    /**
     * Get the levelone locations with id, name and industrycode
     *
     * @param Int $userid The user id
     *
     * @return mixed Array|null The levelone locations
     */
    /**
     * @param           $userid
     * @return          array|null
     * @throws          Exception
     *
     * @updateDate      18/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Add comments, exception ...
     */
    public static function get_levelone_municipalities($userid) {
        /* Variables    */
        global $DB;
        $ids            = array();
        $leveloneobjs   = array();
        $competences    = null;

        try {
            /* Get user competence  */
            $competences = Competence::Get_CompetenceData($userid);

            /* Get Level One connected with user    */
            if ($competences) {
                foreach ($competences as $comp) {
                    if (!in_array($comp->levelOne, $ids)) {
                        $ids[] = $comp->levelOne;
                    }
                }

                /* Get the levelone ids, names and industrycodes with given ids */
                if (!empty($ids)) {
                    /* SQL Instruction  */
                    $sql = " SELECT id,
                                    name,
                                    industrycode
                             FROM   {report_gen_companydata}
                             WHERE  id ";

                    /* Add search criteria  */
                    list($in, $params) = $DB->get_in_or_equal($ids);

                    /* Execute  */
                    $leveloneobjs = $DB->get_records_sql($sql . $in, $params);
                }//if_ids
            }//if_competences

            return $leveloneobjs;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_levelone_municipalities

    /**
     * @param           $leveloneobjsfiltered
     * @return          array|null
     * @throws          Exception
     *
     * @updateDate      17/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get all sectors connected with level one
     */
    public static function get_leveltwo_sectors($leveloneobjsfiltered) {
        /* Variables    */
        global $DB;
        $levelOne       = array();
        $levelSectors   = null;
        $parents        = null;

        try {
            if ($leveloneobjsfiltered) {
                /* Get Level One */
                foreach ($leveloneobjsfiltered as $obj) {
                    $levelOne[$obj->id] = $obj->id;
                }//for_level_one_filtered

                /* Get all sectors connected with level one */
                $parents = implode(',',$levelOne);
                /* SQL Instruction  */
                $sql = "  SELECT    rc.id,
                                    rc.name,
                                    rc.industrycode
                          FROM      {report_gen_companydata}        rc
                              JOIN  {report_gen_company_relation}   rcr ON    rcr.companyid = rc.id
                                                                        AND   rcr.parentid  IN ($parents)
                          WHERE     rc.hierarchylevel = 2
                              AND   rcr.parentid ";

                /* Execute  */
                $levelSectors = $DB->get_records_sql($sql);
            }//if_level_one_filteres

            return $levelSectors;
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_leveltwo_sectors

    /**
     * Get the user's local template category.
     *
     * @return int The category id
     */
    public static function get_localtempcategory() {
        global $DB, $USER;

        $categoryid = 0;

        if ($record = $DB->get_record('friadmin_local_templates', array('userid' => $USER->id))) {
            $categoryid = $record->categoryid;
        }

        return $categoryid;
    }

    /**
     * Check if the user selected template category exists.
     *
     * @return bool True or false
     */
    public static function localtempcategory_exists($catid = 0) {
        global $DB;

        return $DB->record_exists('course_categories', array('id' => $catid));
    }

    /**
     * Get the user's local template category.
     *
     * @return array The course ids for the two template types.
     */
    public static function get_preftemplate_selection() {
        global $DB, $USER;

        $preferredtemplates = array(
            TEMPLATE_TYPE_EVENT => 0,
            TEMPLATE_TYPE_NETCOURSE => 0
        );

        if ($result = $DB->get_records('friadmin_preferred_template', array('userid' => $USER->id))) {
            foreach ($result as $row) {
                if ($row->type == TEMPLATE_TYPE_EVENT) {
                    $preferredtemplates[TEMPLATE_TYPE_EVENT] = $row->courseid;
                } else if ($row->type == TEMPLATE_TYPE_NETCOURSE) {
                    $preferredtemplates[TEMPLATE_TYPE_NETCOURSE] = $row->courseid;
                }
            }
        }

        return $preferredtemplates;
    }

    /**
     * Get the user managed categories and the template list
     *
     * @return      array       An array with the two popup lists
     * @throws      Exception
     */
    public static function get_usercategories_data() {
        /* Variables    */
        global $CFG;
        $result = null;
        $pluginInfo = null;
        $templateCatId = null;
        $courseCat = null;
        $templateCourses = null;

        try {
            require_once($CFG->libdir . '/coursecatlib.php');

            /* Plugin Info      */
            $pluginInfo = get_config('local_friadmin');
            $localtempcategory = self::get_localtempcategory();

            /* Result Structure */
            $result = array(
                'localtempcategory' => $localtempcategory,
                'localtempcategoryexists' => self::localtempcategory_exists($localtempcategory),
                'preftemplates' => self::get_preftemplate_selection(),
                'categories' => array(),
                'eventtemplates' => array(),
                'netcoursetemplates' => array()
            );

            /* Get Categories   */
            $result['categories'] = self::getMyCategories();

            /* Fill the data    */
            if ($pluginInfo) {
                if (isset($pluginInfo->template_category) && ($pluginInfo->template_category)) {
                    /* Get Template Category && Course Category */
                    $templateCatId = $pluginInfo->template_category;

                    /* Get Courses category */
                    $sql = " SELECT c.id,
                                    c.fullname,
                                    c.format,
                                    c.visible
                             FROM   {course} c 
                             WHERE  (c.format like '%classroom%' 
                                     OR  
                                     #c.format like '%elearning%'
                                     #OR  
                                     c.format like '%netcourse%')
                                     AND 
                                     (c.category = $templateCatId
                                     OR
                                     c.category =  $localtempcategory
                                     )";

                    global $DB;
                    $templateCourses = $DB->get_records_sql($sql);

                    /* Add to result Structure  */
                    foreach ($templateCourses as $templateCo) {
                        if ($templateCo->visible) {
                            if (strpos($templateCo->format, 'classroom') !== false) {
                                $result['eventtemplates'][$templateCo->id] = $templateCo->fullname;
                            } else {
                                $result['netcoursetemplates'][$templateCo->id] = $templateCo->fullname;
                            }
                        }//if_visible
                    }//for_templatesCourse
                }//if_template_category
            }//if_pluginInfo

            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//get_popup_data

    /**
     * Get the user related location related menu data for
     * the municipality, sectors and locations
     *
     * @param int|null $userId
     * @param int|null $municipalityid
     * @param int|null $sectorid
     *
     * @return           array
     * @throws           Exception
     */
    public static function get_user_locationdata($userId = null, $municipalityid = null,
        $sectorid = null) {
        global $USER;
        $result = null;
        $leveloneobjs = null;
        $leveloneobjsfiltered = array();
        $userleveloneids = array();

        // Set the $municipalityid to null if it is 0 or -1 to get all sectors and locations.
        if ($municipalityid <= 0) {
            $municipalityid = null;
        }

        // Set the $sectorid to null if it is 0 or -1 to get all locations.
        if ($sectorid <= 0) {
            $sectorid = null;
        }

        try {
            // Result structure.
            $result = array(
                'municipality' => array(),
                'sector' => array(),
                'location' => array()
            );

            if (is_null($userId)) {
                $userId = $USER->id;
            }//id_userid

            // Get the competence related municipalities
            // The $leveloneobjs array contains objects with
            // id, name and industrycode properties.
            $leveloneobjs = self::get_levelone_municipalities($userId);

            //Get all my municipalities.
            foreach ($leveloneobjs as $obj) {
                $result['municipality'][$obj->id] = $obj->name;
                $userleveloneids[] = $obj->id;

                // If a municipality has been selected then use only that one.
                if (!is_null($municipalityid)) {
                    if ($municipalityid == $obj->id) {
                        $leveloneobjsfiltered[] = $obj;
                    }
                } else {
                    $leveloneobjsfiltered[] = $obj;
                }
            }//for_levelone_obj

            //Get all categories where the user is a super user.
            //$myCategories = self::getMyCategories();

            if (!empty($leveloneobjsfiltered)) {
                // Get the sectors for the relevant municipalities via industrycodes.
                $leveltwoobjs = self::get_leveltwo_sectors($leveloneobjsfiltered);

                foreach ($leveltwoobjs as $obj) {
                    if (!in_array($obj->id, $result['sector'])) {
                        $result['sector'][$obj->id] = $obj->name;
                    }
                }

                if (is_null($sectorid)) {
                    // Get the locations for the relevant municipalities via levelone ids
                    $locationsobjs = self::get_locations($leveloneobjsfiltered);
                } else {
                    $locationsobjs = self::get_locations_for_sector($sectorid);
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

    /*********************/
    /* PRIVATE FUNCTIONS */
    /*********************/

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

    /**
     * @param           $categoryId
     * @param           $myCategories
     * @throws          Exception
     *
     * @creationDate    18/06/2015
     * @author          eFaktor     (fbv)
     *
     * Description
     * Get and add the subcategories connected with to my categories list
     */
    private static function getSubcategories($categoryId,&$myCategories) {
        /* Variables    */
        global $DB;

        try {
            /* Search Criteria  */
            $params = array();
            $params['parent'] = $categoryId;

            /* Execute  */
            $rdo = $DB->get_records('course_categories',$params);
            if ($rdo) {
                /* Add subcategories to my categories list  */
                foreach ($rdo as $instance) {
                    $myCategories[$instance->id] = $instance->name;
                }//for_rdo
            }//if_rdo
        }catch (Exception $ex) {
            throw $ex;
        }//try_catch
    }//getSubcategories
}
